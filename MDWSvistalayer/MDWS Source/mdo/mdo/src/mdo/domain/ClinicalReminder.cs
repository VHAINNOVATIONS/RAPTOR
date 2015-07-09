using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class ClinicalReminder
    {
        string id;
        string name;

        const string DAO_NAME = "IRemindersDao";

        public ClinicalReminder() { }

        public ClinicalReminder(string id, string name)
        {
            Id = id;
            Name = name;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        internal static IRemindersDao getDao(AbstractConnection cxn)
        {
            if (!cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: unconnected");
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getRemindersDao(cxn);
        }

        public static string[] getReminderReportTemplates(AbstractConnection cxn)
        {
            return getDao(cxn).getReminderReportTemplates();
        }

        public static OrderedDictionary getActiveReminderReports(AbstractConnection cxn)
        {
            return getDao(cxn).getActiveReminderReports();
        }

        public static ReminderReportPatientList getPatientListForReminderReport(AbstractConnection cxn, string rptId)
        {
            return getDao(cxn).getPatientListForReminderReport(rptId);
        }
    }
}
