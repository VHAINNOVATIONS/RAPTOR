using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Zipcode
    {
        string code;
        string city;
        string state;
        string stateAbbr;

        public Zipcode(string code, string city, string state, string stateAbbr)
        {
            Code = code;
            City = city;
            State = state;
            StateAbbr = stateAbbr;
        }

        public Zipcode() { }

        public string Code
        {
            get { return code; }
            set { code = value; }
        }

        public string City
        {
            get { return city; }
            set { city = value; }
        }

        public string State
        {
            get { return state; }
            set { state = value; }
        }

        public string StateAbbr
        {
            get { return stateAbbr; }
            set { stateAbbr = value; }
        }
    }
}
