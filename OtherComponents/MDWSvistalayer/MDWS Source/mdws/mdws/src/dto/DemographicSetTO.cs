using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DemographicSetTO
    {
        public string tag;
        public AddressTO[] addresses;
        public PhoneNumTO[] phones;
        public string[] emailAddresses;
        public string[] names;
        private Dictionary<string, DemographicSet> dictionary;

        public DemographicSetTO() { }

        public DemographicSetTO(string key, DemographicSet mdo)
        {
            this.tag = key;
            setDemographics(mdo);
        }

        public DemographicSetTO(KeyValuePair<string, DemographicSet> mdo)
        {
            this.tag = mdo.Key;
            setDemographics(mdo.Value);
        }

        public DemographicSetTO(Dictionary<string, DemographicSet> dictionary)
        {
            if (dictionary == null)
            {
                return;
            }
            if (dictionary.Keys.Count == 1)
            {
                this.tag = dictionary.Keys.First();
            }
            setDemographics(dictionary[dictionary.Keys.First()]);
        }

        void setDemographics(DemographicSet mdo)
        {
            if (mdo.StreetAddresses != null && mdo.StreetAddresses.Count > 0)
            {
                this.addresses = new AddressTO[mdo.StreetAddresses.Count];
                for (int i = 0; i < mdo.StreetAddresses.Count; i++)
                {
                    this.addresses[i] = new AddressTO(mdo.StreetAddresses[i]);
                }
            }
            if (mdo.PhoneNumbers != null && mdo.PhoneNumbers.Count > 0)
            {
                this.phones = new PhoneNumTO[mdo.PhoneNumbers.Count];
                for (int i = 0; i < mdo.PhoneNumbers.Count; i++)
                {
                    this.phones[i] = new PhoneNumTO(mdo.PhoneNumbers[i]);
                }
            }
            if (mdo.EmailAddresses != null && mdo.EmailAddresses.Count > 0)
            {
                this.emailAddresses = new string[mdo.EmailAddresses.Count];
                for (int i = 0; i < mdo.EmailAddresses.Count; i++)
                {
                    this.emailAddresses[i] = mdo.EmailAddresses[i].Address;
                }
            }
            if (mdo.Names != null && mdo.Names.Count > 0)
            {
                this.names = new string[mdo.Names.Count];
                for (int i = 0; i < mdo.Names.Count; i++)
                {
                    this.names[i] = mdo.Names[i].getLastNameFirst(); ;
                }
            }
        }
    }
}