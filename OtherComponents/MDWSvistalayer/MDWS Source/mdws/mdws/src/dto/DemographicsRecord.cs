using System;
using System.Collections.Generic;

/// <summary>
/// Summary description for DemographicsRecord
/// </summary>
namespace gov.va.medora.mdws.dto
{
    public class DemographicsRecord
    {
        public string name;
        public string gender;
        public string dob;
        public string address;
        public string city;
        public string state;
        public string zipcode;
        public string phone;
        public string email;
        public string source;

        public DemographicsRecord() { }

        public DemographicsRecord(PersonTO person, string source)
        {
            this.name = person.name;
            this.gender = person.gender;
            this.dob = person.dob;
            if (person.demographics != null)
            {
                setAddressProperties(person.demographics[0]);
                setPhone(person.demographics[0]);
                setEmail(person.demographics[0]);
            }
            this.source = source;
        }
        internal void setAddressProperties(DemographicSetTO demoSet)
        {
            if (demoSet.addresses != null)
            {
                AddressTO addr = demoSet.addresses[0];
                string streetAddress = addr.streetAddress1;
                if (!String.IsNullOrEmpty(addr.streetAddress2))
                {
                    streetAddress += '\n' + addr.streetAddress2;
                }
                if (!String.IsNullOrEmpty(addr.streetAddress3))
                {
                    streetAddress += '\n' + addr.streetAddress3;
                }
                this.address = streetAddress;
                this.city = addr.city;
                this.state = addr.state;
                this.zipcode = addr.zipcode;
            }
        }

        internal void setPhone(DemographicSetTO demoSet)
        {
            if (demoSet.phones != null)
            {
                this.phone = formattedPhoneNumber(demoSet.phones[0]);
            }
        }

        internal string formattedPhoneNumber(PhoneNumTO num)
        {
            //string result = '(' + num.areaCode + ')' + num.exchange + '-' + num.number;
            string result = num.number;
            if (!string.IsNullOrEmpty(num.description))
            {
                result += " (" + num.description + ")";
            }
            return result;
        }

        internal void setEmail(DemographicSetTO demoSet)
        {
            if (demoSet.emailAddresses != null)
            {
                this.email = demoSet.emailAddresses[0] + "\r\n";
            }
        }
    }
}