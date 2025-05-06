using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Threading;
using System.DirectoryServices.AccountManagement;
using System.Windows.Forms;
using System.DirectoryServices;

namespace MyApplication
{
    public sealed class Login
    {
        private static Login currentLogin = null;
        private static readonly object loginLock = new object();
        private UserPrincipal loggedInUser = null;
        private string _userTitle = null;

	    /*
        * Create a new Login object and log in the user.
        * Use the current Windows user when logging in.
        */
        private Login()
        {
            UserLogin(true);
        }
	
        /*
        * Obtain lock and create the Login Object if it does not already exist.
        * Return the Login object.
        */
        public static Login GetLogin
        {
            get
            {
                    lock(loginLock)
                    {
                        if(currentLogin == null)
                        {
                            currentLogin = new Login();
                        }

                        return currentLogin;
                    }
            } 
        }

        /*
        * Return the UserPricipal for the Login object.
        */
        public UserPrincipal GetUserPrincipal
        {
            get
            {
                //Login temp = GetLogin;
                return loggedInUser;
            }
        }

        /*
        * Return the title property of UserPricipal for the Login object.
        */
        public string UserTitle
        {
            get
            {
                DirectoryEntry d = (DirectoryEntry)GetUserPrincipal.GetUnderlyingObject();
                _userTitle = d.Properties["Title"]?.Value?.ToString();
                
                return _userTitle;
            }
            private set
            {
                _userTitle = value;
            }
        }

        /*
        * Set up the UserPrinciple object within the login object.
        * Handle any exceptions thrown while attempting to set the UserPrinciple object. 
        */
        public void UserLogin(bool Auto)
        {
            bool retry;
            do
            {
                retry = false;
                try
                {
                    if(Auto)
                    {
                        AutoLogin();
                    }
                    else
                    {
                        ManualLogin();
                    }

                }
                catch (System.DirectoryServices.AccountManagement.PrincipalServerDownException ex)
                {
		            // Display the FailedLoginForm prompting the user to ensure connection to VPN data tunnel.
                    Form failed = new FailedLoginForm();
                    var result = failed.ShowDialog();
		    
		            // Attempt to login again if user selects retry.
                    if(result == DialogResult.Retry)
                    {
                        //System.Windows.Forms.MessageBox.Show("Exception: " + ex.Message);
                        retry = true;
                    }

                    // Not able to access work network. Make sure you are connected to VPN if working remotely.
                }
                catch (Exception LoginException)
                {
                    System.Windows.Forms.MessageBox.Show("Unexpected Login Exception:\n\t" + LoginException.GetType() + " " + LoginException.HResult + "\n\t" + LoginException.Message + "\n\nStackTrace:\n\t" + LoginException.StackTrace);
                }
            } while (retry);
            
        }
        
        /*
        * Call to set the current windows user as the logged in user in the application.
        */
        private void AutoLogin()
        {
            // Get the UserPrincipal of the user currently logged into windows.
            PrincipalContext ctx = new PrincipalContext(System.DirectoryServices.AccountManagement.ContextType.Domain, "Company.Domain");
            UserPrincipal user = UserPrincipal.FindByIdentity(ctx, System.Security.Principal.WindowsIdentity.GetCurrent().Name);           
            SetUser(user);

            /*
             * Changed from using UserPrincipal.Current due to an error with the .net framework here.
             */
            //SetUser(UserPrincipal.Current);

        }

        /*
        * Call to use a different user than the current windows user.
        * Not yet implimented, but could be a future enhancement.
        */
        private void ManualLogin()
        {
            // will probably use this to set this up.
            // https://docs.microsoft.com/en-us/dotnet/api/system.security.principal.windowsimpersonationcontext?view=netframework-4.8
            /*
            System.DirectoryServices.AccountManagement.PrincipalContext context;
            string accountName;
            string pass; // make encrypted.
            bool enabled;
            */
        }

        /*
        * Set the UserPricipal object for the login class to the passed UserPrincipal.
        */
        private void SetUser(UserPrincipal setAsUser)
        {
            if (loggedInUser == null)
            {
                loggedInUser = setAsUser;
            }
        }
        
    }
}