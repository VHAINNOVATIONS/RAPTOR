using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PhoneNumTO : AbstractTO
    {
        public string type;
        public string areaCode;
        public string exchange;
        public string number;
        public string description;

        public PhoneNumTO() { }

        public PhoneNumTO(PhoneNum mdo)
        {
            this.areaCode = mdo.AreaCode;
            this.exchange = mdo.Exchange;
            this.number = mdo.Number;
            this.description = mdo.Description;
        }
    }
}
