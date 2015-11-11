using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class Patient : gov.va.medora.mdo.domain.sm.User
    {

        private DateTime _dob;

        public DateTime Dob
        {
            get { return _dob; }
            set { _dob = value; }
        }
        private string _icn;

        public string Icn
        {
            get { return _icn; }
            set { _icn = value; }
        }
        private List<PatientFacility> _facilities;

        public List<PatientFacility> Facilities
        {
            get { return _facilities; }
            set { _facilities = value; }
        }
        private DateTime _relationshipUpdate;

        public DateTime RelationshipUpdate
        {
            get { return _relationshipUpdate; }
            set { _relationshipUpdate = value; }
        }

        public Patient()
        {
            //UserType = UserTypeEnum.PATIENT;
            ParticipantType = ParticipantTypeEnum.PATIENT;
        }
    }
}
