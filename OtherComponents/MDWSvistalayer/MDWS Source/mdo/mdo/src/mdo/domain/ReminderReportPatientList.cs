using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class ReminderReportPatientList
    {
        string reportId;
        string reportName;
        string reportTimestamp;
        List<PatientListEntry> patients;

        public ReminderReportPatientList()
        {
            patients = new List<PatientListEntry>();
        }

        public string ReportId
        {
            get { return reportId; }
            set { reportId = value; }
        }

        public string ReportName
        {
            get { return reportName; }
            set { reportName = value; }
        }

        public string ReportTimestamp
        {
            get { return reportTimestamp; }
            set { reportTimestamp = value; }
        }

        public List<PatientListEntry> Patients
        {
            get { return patients; }
        }

        public void AddPatient(int listId, string name, string pid, string ssn)
        {
            PatientListEntry entry = new PatientListEntry(listId, name, pid, ssn);
            patients.Add(entry);
        }
    }
}
