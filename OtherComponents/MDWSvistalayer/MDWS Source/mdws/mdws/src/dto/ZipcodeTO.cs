using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ZipcodeTO : AbstractTO
    {
        public string code;
        public string city;
        public string state;
        public string stateAbbr;

        public ZipcodeTO() { }

        public ZipcodeTO(Zipcode mdoZip)
        {
            this.code = mdoZip.Code;
            this.city = mdoZip.City;
            this.state = mdoZip.State;
            this.stateAbbr = mdoZip.StateAbbr;
        }
    }
}
