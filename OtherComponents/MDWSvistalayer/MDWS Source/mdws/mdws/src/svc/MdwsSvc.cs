using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Services;
using gov.va.medora.mdws.dao.sql;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for MdwsSvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/MdwsSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [System.ComponentModel.ToolboxItem(false)]

    public partial class MdwsSvc : BaseService
    {

        [WebMethod(Description = "Get all the MDWS sessions for a date range")]
        public ApplicationSessionsTO getMdwsSessions(string startDate, string endDate)
        {
            ApplicationSessionsTO result = new ApplicationSessionsTO();
            UsageDao dao = null;
            try
            {
                dao = new UsageDao();
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
                return result;
            }

            DateTime start = new DateTime();
            DateTime end = new DateTime();
            if (String.IsNullOrEmpty(startDate) || String.IsNullOrEmpty(endDate))
            {
                result.fault = new FaultTO("Must supply a start and a stop date");
            }
            else if (!DateTime.TryParse(startDate, out start))
            {
                result.fault = new FaultTO("Unable to parse start date", "Try a different date format");
            }
            else if (!DateTime.TryParse(endDate, out end))
            {
                result.fault = new FaultTO("Unable to parse end date", "Try a different date format");
            }
            if (start.Year < 2010 || end.Year < 2010)
            {
                result.fault = new FaultTO("No records exist from before 2010");
            }
            else if (end.CompareTo(start) < 0)
            {
                result.fault = new FaultTO("The end date must be the same or later than the start date");
            }
            else if (end.Subtract(start).TotalDays > 30)
            {
                result.fault = new FaultTO("You are only allowed to retrieve a 30 day window of session data");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                result = dao.getSessions(start, end);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        [WebMethod(Description = "Get all the in memory MDWS sessions")]
        public ApplicationSessionsTO getMdwsSessionsInMemory()
        {
            ApplicationSessionsTO result = new ApplicationSessionsTO();
            if (Application == null || Application.AllKeys == null || !Application.AllKeys.Contains("APPLICATION_SESSIONS"))
            {
                return result;
            }
            ApplicationSessions sessions = (ApplicationSessions)Application["APPLICATION_SESSIONS"];
            if (sessions.Sessions == null || sessions.Sessions.Count == 0)
            {
                return result;
            }

            result.sessions = new ApplicationSessionTO[sessions.Sessions.Count];
            int currentIndex = 0;
            foreach (string sessionId in sessions.Sessions.Keys)
            {
                result.sessions[currentIndex] = new ApplicationSessionTO(sessions.Sessions[sessionId]);
                currentIndex++;
            }
            return result;
        }
    }
}
