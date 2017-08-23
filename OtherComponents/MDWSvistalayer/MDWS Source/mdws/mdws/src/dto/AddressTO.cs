using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AddressTO : AbstractTO
    {
        public string validFrom;
        public string validTo;
        public string description;
        public string type;
        public string streetAddress1;
        public string streetAddress2;
        public string streetAddress3;
        public string city;
        public string county;
        public string state;
        public string zipcode;
        public string country;

        public AddressTO() { }

        public AddressTO(Address mdo)
        {
            this.validFrom = mdo.ValidFrom;
            this.validTo = mdo.ValidTo;
            this.country = mdo.Country;
            this.streetAddress1 = mdo.Street1;
            this.streetAddress2 = mdo.Street2;
            this.streetAddress3 = mdo.Street3;
            this.city = mdo.City;
            this.county = mdo.County;
            this.state = mdo.State;
            this.zipcode = mdo.Zipcode;
            this.description = mdo.Description;
        }
    }
}
