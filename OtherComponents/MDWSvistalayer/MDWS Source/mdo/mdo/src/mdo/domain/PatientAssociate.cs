using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PatientAssociate : Person
    {
        string association;
        string relationshipToPatient;
        string facilityName;

        public PatientAssociate() { }

        public string Association
        {
            get { return association; }
            set { association = value; }
        }

        public string RelationshipToPatient
        {
            get { return relationshipToPatient; }
            set { relationshipToPatient = value; }
        }

        public string FacilityName
        {
            get { return facilityName; }
            set { facilityName = value; }
        }
    }
}
