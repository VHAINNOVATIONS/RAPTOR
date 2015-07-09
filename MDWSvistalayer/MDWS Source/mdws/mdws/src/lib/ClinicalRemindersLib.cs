using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using gov.va.medora.mdo;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class ClinicalRemindersLib
    {
        MySession mySession;

        public ClinicalRemindersLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TextArray getReminderReportTemplates()
        {
            TextArray result = new TextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                string[] templates = ClinicalReminder.getReminderReportTemplates(mySession.ConnectionSet.BaseConnection);
                result = new TextArray(templates);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getActiveReminderReports()
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                OrderedDictionary d = ClinicalReminder.getActiveReminderReports(mySession.ConnectionSet.BaseConnection);
                result = new TaggedTextArray(d);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public ReminderReportPatientListTO getPatientListForReminderReport(string rptId)
        {
            ReminderReportPatientListTO result = new ReminderReportPatientListTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(rptId))
            {
                result.fault = new FaultTO("Empty rptId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                ReminderReportPatientList lst = ClinicalReminder.getPatientListForReminderReport(mySession.ConnectionSet.BaseConnection, rptId);
                result = new ReminderReportPatientListTO(lst);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }
    }
}