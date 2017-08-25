using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Ward : HospitalLocation
    {
        string _name;

        public string WardName
        {
            get { return _name; }
            set { _name = value; }
        }

        public string HospitalLocationName
        {
            get { return Name; }
            set { Name = value; }
        }
    }
}
