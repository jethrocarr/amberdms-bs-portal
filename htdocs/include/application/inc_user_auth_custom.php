<?php
/*
	include/application/inc_user_auth_custom.php

	Provides custom authentication routines to enable this portal application
	to authentication via a custom SOAP API to the Amberdms Billing System.
*/



/*
	CLASS user_auth_custom

	Custom user authentication framework, called by the user_auth framework
	components of the Amberphplib framework as required.

	This class has been specifically written to communicate with the ABS
	SOAP API to authentication against the customer portal credenticals information.
*/
class user_auth_custom
{
	var $soap_api_sessionid;	// session ID of remote SOAP API (if successful connection is established)


	/*
		constructor
	*/
	function user_auth_custom()
	{

		// if we have already done the authentication, we will want the session ID for the SOAP API
		// to use for checking stuff like user options and permissions.
		if ($_SESSION["user"]["soapsession"])
		{
			$this->soap_api_sessionid = $_SESSION["user"]["soapsession"];
		}

	}



	/*
		login_authenticate

		Verifies if the supplied username and password is valid.

		Fields
		portal_username		code_customer field in ABS.
		portal_password		portal password for the customer.

		Returns
		-2			SOAP API user authentication failure.
		-1			Unknown Failure
		0			Customer authentication failure.
		#			Successful authentication, id of customer returned
	*/

	function login_authenticate($portal_username, $portal_password)
	{
		log_write("debug", "user_auth_custom", "Executing login_authenticate($portal_username, $portal_password)");


		/*
			First stage is to authenticate with the API using the master
			login details and get our PHP session ID returned.
		*/

		$client = new SoapClient($GLOBALS["config"]["abs_soap"]["url"] ."/authenticate/authenticate.wsdl");
		$client->__setLocation($GLOBALS["config"]["abs_soap"]["url"] ."/authenticate/authenticate.php");

		try
		{
			$this->soap_api_sessionid = $client->login($GLOBALS["config"]["abs_soap"]["account"], $GLOBALS["config"]["abs_soap"]["username"], $GLOBALS["config"]["abs_soap"]["password"]);
		}
		catch (SoapFault $exception)
		{
			log_write("error", "user_auth_custom", "An unexpected error occured with the SOAP API \"". $exception->getMessage() ."\"");
			return -2;
		}


		/*
			Now that we have an established session, query and attempt to verify the customer
			portal authentication information.
		*/

		$client = new SoapClient($GLOBALS["config"]["abs_soap"]["url"] ."/customers/customers_manage.wsdl");
		$client->__setLocation($GLOBALS["config"]["abs_soap"]["url"] ."/customers/customers_manage.php?". $this->soap_api_sessionid);

		try
		{
			$id = $client->customer_portal_auth(0, $portal_username, $portal_password);
		}
		catch (SoapFault $exception)
		{
			log_write("warning", "user_auth_custom", "Whilst attempting to authenticate the customer portal, the following error was encountered \"". $exception->getMessage() ."\"");
			return 0;
		}


		// verify success
		if ($id)
		{
			log_write("debug", "user_auth_custom", "Successful authentication against customer portal database");
			return $id;
		}

		return -1;

	} // end of login_authenticate



	/*
		session_init

		Sets session options for the user - currently this is just the SOAP API session ID, but
		in future will include stuff such as customer perferences from ABS.

		Returns
		0	Failure
		1	Success
	*/
	
	function session_init()
	{
		log_write("debug", "user_auth_custom", "Executing session_init()");


		// save the SOAP session ID
		$_SESSION["user"]["soapsession"] = $this->soap_api_sessionid;


		return 1;
		
	} // end of session_init





	/*
		permissions_init

		Load and return all the permissions for the customers.

		Returns
		0		Failure
		1		Success
	*/
	function permissions_init()
	{
		/*
			We have as static list of permissions for all users
		*/

		$GLOBALS["cache"]["user"]["perms"]["customer"]	= 1;

	} // end of permissions_init


} // end of class user_auth_custom


?>
