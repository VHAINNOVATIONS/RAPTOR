using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace gov.va.medora.mdws.Web
{
    /// <summary>
    /// This page displays summary information about the current MDWS sessions. Session information
    /// is added to this page as new sessions are requested by client applications. You must be a member
    /// of the local admin group to view the contents of this page.
    /// </summary>
    public partial class Dashboard : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            try
            {
                //if (!HttpContext.Current.User.IsInRole("Builtin\\Administrators"))
                //{
                //    labelMessage.Text = "You must be a member of the server's Administrators group to view this page. " +
                //        "(logged on as: " + HttpContext.Current.User.Identity.Name + ")";
                //    panelDashboard.Visible = false;
                //    return;
                //}
                //else
                //{
                    panelDashboard.Visible = true;
                    Application.Lock();
                    ApplicationSessions sessions = Application["APPLICATION_SESSIONS"] as ApplicationSessions;

                    Application.UnLock();

                    TimeSpan upTime = DateTime.Now.Subtract(sessions.Start);
                    labelUpTime.Text = upTime.Days + " Days, " + upTime.Hours + " Hours &amp; " + upTime.Minutes + " Minutes";
                    repeaterSession.DataSource = sessions.Sessions.Values;
                    repeaterSession.DataBind();
                    labelSessionCount.Text = sessions.Sessions.Count.ToString();
                //}
            }
            catch (Exception exc)
            {
                labelMessage.Text = "Oops! An unexpected error occurred. <br />" + exc.Message;
            }
        }

        protected string getDuration(object start)
        {
            return (DateTime.Now.Subtract((DateTime)start)).ToString();
        }
    }
}
