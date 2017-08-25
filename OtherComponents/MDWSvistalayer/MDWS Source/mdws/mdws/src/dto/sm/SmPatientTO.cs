using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class SmPatientTO : BaseSmTO
    {
        public string icn;
        //public List<PatientFacility> facilities;
        public DateTime relationshipUpdate;

        public SmPatientTO() { }

        public SmPatientTO(mdo.domain.sm.Patient patient)
        {
            id = patient.Id;
            icn = patient.Icn;
            relationshipUpdate = patient.RelationshipUpdate;
        }
    }
}
