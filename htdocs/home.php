<?php
/*
	home.php

	Customer dashboard/landing page.
*/

if (!user_online())
{
	// Because this is the default page to be directed to, if the user is not
	// logged in, they should go straight to the login page.
	//
	// All other pages will display an error and prompt the user to login.
	//
	include_once("user/login.php");
}
else
{
	class page_output
	{
		function check_permissions()
		{
			// this page has a special method for handling permissions - please refer to code comments above
			return 1;
		}


		function check_requirements()
		{
			// nothing todo
			return 1;
		}
		
		function execute()
		{
			// nothing todo
			return 1;
		}

		function render_html()
		{
			print "<h3>ABS CUSTOMER PORTAL</h3>";
			print "<p>Welcome to the Amberdms Billing System Customer Portal, here you can view past invoices, service details like data usage and CDR billing, and more.</p>";
		}
	}
}


?>
