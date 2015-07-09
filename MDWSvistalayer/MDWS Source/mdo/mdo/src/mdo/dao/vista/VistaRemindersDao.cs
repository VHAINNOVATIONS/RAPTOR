using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaRemindersDao : IRemindersDao
    {
        AbstractConnection cxn = null;

        public VistaRemindersDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public string[] getReminderReportTemplates()
        {
            MdoQuery request = builtGetReminderReportTemplatesRequest();
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL CPGPI"));
            return StringUtils.split(response, StringUtils.CRLF);
        }

        internal MdoQuery builtGetReminderReportTemplatesRequest()
        {
            VistaQuery vq = new VistaQuery("AMOJVL CPGPI GETCPGPIS");
            return vq;
        }

        public OrderedDictionary getActiveReminderReports()
        {
            MdoQuery request = builtGetActiveReminderReportsRequest();
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL CPGPI"));
            return reminderReportNamesToMdo(response);
        }

        internal MdoQuery builtGetActiveReminderReportsRequest()
        {
            VistaQuery vq = new VistaQuery("AMOJVL CPGPI GETARPT");
            return vq;
        }

        internal OrderedDictionary reminderReportNamesToMdo(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            OrderedDictionary result = new OrderedDictionary();
            for (int i = 0; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(flds[1], flds[0]);
            }
            return result;
        }

        public ReminderReportPatientList getPatientListForReminderReport(string rptId)
        {
            MdoQuery request = builtGetPatientListForReminderReportRequest(rptId);
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL CPGPI"));
            ReminderReportPatientList result = toPatientListMdo(response);
            result.ReportId = rptId;
            return result;
        }

        internal MdoQuery builtGetPatientListForReminderReportRequest(string rptId)
        {
            VistaQuery vq = new VistaQuery("AMOJVL CPGPI GETPLIST");
            vq.addParameter(vq.LITERAL, rptId);
            return vq;
        }

        internal ReminderReportPatientList toPatientListMdo(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ReminderReportPatientList result = new ReminderReportPatientList();
            string[] flds = StringUtils.split(lines[0], StringUtils.CARET);
            result.ReportName = flds[1];
            flds = StringUtils.split(lines[1], StringUtils.CARET);
            result.ReportTimestamp = flds[1];
            for (int i = 2; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                flds = StringUtils.split(lines[i], StringUtils.CARET);
                // check for flds[1] being integer
                result.AddPatient(Convert.ToInt16(flds[1]), flds[3],flds[4],flds[5]);
            }
            return result;
        }
    }
}
