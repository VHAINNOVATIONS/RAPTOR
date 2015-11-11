using System;
using System.Collections.Generic;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientListEntryTO : AbstractTO
    {
        public int listId;
        public string patientName;
        public string patientId;
        public string ssn;

        public PatientListEntryTO() { }

        public PatientListEntryTO(PatientListEntry mdo)
        {
            this.listId = mdo.ListId;
            this.patientName = mdo.PatientName;
            this.patientId = mdo.PatientId;
            this.ssn = mdo.SSN;
        }
    }
}