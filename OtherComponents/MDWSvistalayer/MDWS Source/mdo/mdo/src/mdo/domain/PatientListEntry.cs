using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PatientListEntry
    {
        int listId;
        string patientName;
        string patientId;
        string ssn;

        public PatientListEntry() { }

        public PatientListEntry(int listId, string patientName, string pid, string ssn)
        {
            ListId = listId;
            PatientName = patientName;
            PatientId = pid;
            SSN = ssn;
        }

        public int ListId
        {
            get { return listId; }
            set { listId = value; }
        }

        public string PatientName
        {
            get { return patientName; }
            set { patientName = value; }
        }

        public string PatientId
        {
            get { return patientId; }
            set { patientId = value; }
        }

        public string SSN
        {
            get { return ssn; }
            set { ssn = value; }
        }
    }
}
