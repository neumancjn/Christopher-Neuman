using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Threading;

namespace MyApplication
{
    public partial class SplashForm : Form
    {
        private static readonly object loginLock = new object();

        //Delegate for cross thread call to close
        private delegate void CloseDelegate();

        private delegate void StepDelegate();

        private static SplashForm splashForm;

        /*
        * Create SplashForm
        */
        private SplashForm()
        {
            InitializeComponent();
        }

        /*
        * Create an instance of and display the splashForm using a new thread.
        */
        static public void ShowLoadingScreen()
        {
            // Make sure it is only launched once.    
            if (splashForm != null) return;
            splashForm = new SplashForm();
            Thread thread = new Thread(new ThreadStart(SplashForm.ShowForm));
            thread.IsBackground = true;
            thread.SetApartmentState(ApartmentState.STA);
            thread.Start();
        }

        static private void ShowForm()
        {
            if (splashForm != null) Application.Run(splashForm);
        }

        static public void CloseForm()
        {
            splashForm?.Invoke(new CloseDelegate(SplashForm.CloseFormInternal));
        }
        

        static private void CloseFormInternal()
        {
            if (splashForm != null)
            {
                splashForm.Close();
                splashForm = null;
            };
        }

        static public void ProgressBarStep()
        {
            try
            {
                splashForm?.Invoke(new StepDelegate(SplashForm.ProgressStepInternal));
            }
            catch (System.InvalidOperationException x)
            {
                System.Windows.Forms.MessageBox.Show("Cannot invoke SplashForm before it has been created.");
            }

        }
	
        /*
        * Update the progress bar.
        */
        static private void ProgressStepInternal()
        {
            if (splashForm != null && splashForm.splashProgressBar != null)
            {
                try
                {
                    splashForm.splashProgressBar.PerformStep();
                }
                catch (ArgumentException progressBarEx)
                {
                    System.Windows.Forms.MessageBox.Show(progressBarEx.Message);
                }

            };

        }
    }
}