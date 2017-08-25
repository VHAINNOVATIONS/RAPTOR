using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class PatientFacility : BaseModel
    {
        private User _user;

        public User User
        {
            get { return _user; }
            set { _user = value; }
        }
        private string _stationNo;

        public string StationNo
        {
            get { return _stationNo; }
            set { _stationNo = value; }
        }
        private string _dfn;

        public string Dfn
        {
            get { return _dfn; }
            set { _dfn = value; }
        }
    }
}
