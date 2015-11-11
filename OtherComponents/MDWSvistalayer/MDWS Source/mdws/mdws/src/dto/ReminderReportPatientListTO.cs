using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ReminderReportPatientListTO : AbstractTO
    {
        public string reportId;
        public string reportName;
        public string reportTimestamp;
        public PatientListEntryTO[] patients;

        public ReminderReportPatientListTO() { }

        public ReminderReportPatientListTO(ReminderReportPatientList mdo)
        {
            this.reportId = mdo.ReportId;
            this.reportName = mdo.ReportName;
            this.reportTimestamp = mdo.ReportTimestamp;
            if (mdo.Patients != null && mdo.Patients.Count > 0)
            {
                this.patients = new PatientListEntryTO[mdo.Patients.Count];
                for (int i = 0; i < mdo.Patients.Count; i++)
                {
                    this.patients[i] = new PatientListEntryTO(mdo.Patients[i]);
                }
            }
        }
    }
}