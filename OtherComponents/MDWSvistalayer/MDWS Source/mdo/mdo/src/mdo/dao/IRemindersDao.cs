using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IRemindersDao
    {
        string[] getReminderReportTemplates();
        OrderedDictionary getActiveReminderReports();
        ReminderReportPatientList getPatientListForReminderReport(string rptId);
    }
}
