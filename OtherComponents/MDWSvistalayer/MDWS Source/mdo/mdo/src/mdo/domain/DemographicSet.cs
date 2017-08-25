using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class DemographicSet
    {
        List<Address> addresses;
        List<PhoneNum> phones;
        List<EmailAddress> emailAddresses;
        List<PersonName> names;

        public DemographicSet() 
        {
            StreetAddresses = new List<Address>();
            PhoneNumbers = new List<PhoneNum>();
            EmailAddresses = new List<EmailAddress>();
            Names = new List<PersonName>();
        }

        public List<Address> StreetAddresses
        {
            get { return addresses; }
            set { addresses = value; }
        }

        public List<PhoneNum> PhoneNumbers
        {
            get { return phones; }
            set { phones = value; }
        }

        public List<EmailAddress> EmailAddresses
        {
            get { return emailAddresses; }
            set { emailAddresses = value; }
        }

        public List<PersonName> Names
        {
            get { return names; }
            set { names = value; }
        }


        /// <summary>
        /// Calls ToString().GetHashCode() to retrieve the hashcode of the string representation of an DemographicSet object
        /// </summary>
        /// <returns>Int32 HashCode</returns>
        public override int GetHashCode()
        {
            return this.ToString().GetHashCode();
        }

        /// <summary>
        /// Compares the email addresses, phone numbers and addresses for equality
        /// </summary>
        /// <param name="obj">DemographicSet</param>
        /// <returns>True if all internal objects are equal</returns>
        public override bool Equals(object obj)
        {
            if (!(obj is DemographicSet))
            {
                return false;
            }
            DemographicSet temp = obj as DemographicSet;
            // first check email addresses for differences
            if (temp.EmailAddresses != null && this.emailAddresses == null)
            {
                return false;
            }
            if (temp.EmailAddresses == null && this.emailAddresses != null)
            {
                return false;
            }
            if (temp.EmailAddresses != null && this.emailAddresses != null)
            {
                if (temp.EmailAddresses.Count != this.emailAddresses.Count)
                {
                    return false;
                }
                foreach (EmailAddress tempAddr in temp.EmailAddresses)
                {
                    bool found = false;
                    foreach (EmailAddress thisAddr in this.emailAddresses)
                    {
                        if (thisAddr.Equals(tempAddr))
                        {
                            found = true;
                        }
                    }
                    if (!found)
                    {
                        return false;
                    }
                }
            }
            // second check street addresses for differences
            if (temp.StreetAddresses != null && this.addresses == null)
            {
                return false;
            }
            if (temp.StreetAddresses == null && this.addresses != null)
            {
                return false;
            }
            if (temp.StreetAddresses != null && this.addresses != null)
            {
                if (temp.StreetAddresses.Count != this.addresses.Count)
                {
                    return false;
                }
                foreach (Address tempAddr in temp.StreetAddresses)
                {
                    bool found = false;
                    foreach (Address thisAddr in this.addresses)
                    {
                        if (thisAddr.Equals(tempAddr))
                        {
                            found = true;
                        }
                    }
                    if (!found)
                    {
                        return false;
                    }
                }
            }
            // and third check the phone numbers
            if (temp.PhoneNumbers != null && this.phones == null)
            {
                return false;
            }
            if (temp.PhoneNumbers == null && this.phones != null)
            {
                return false;
            }
            if (temp.PhoneNumbers != null && this.phones != null)
            {
                if (temp.PhoneNumbers.Count != this.phones.Count)
                {
                    return false;
                }
                foreach (PhoneNum tempPhone in temp.PhoneNumbers)
                {
                    bool found = false;
                    foreach (PhoneNum thisPhone in this.phones)
                    {
                        if (thisPhone.Equals(tempPhone))
                        {
                            found = true;
                        }
                    }
                    if (!found)
                    {
                        return false;
                    }
                }
            }
            // if all these checks pass then return true
            return true;
        }

        /// <summary>
        /// Concatenate addresses, email addresses, and phone numbers together
        /// </summary>
        /// <returns></returns>
        public override string ToString()
        {
            StringBuilder sb = new StringBuilder();
            sb.Append("Addresses:");
            if (this.addresses != null && this.addresses.Count > 0)
            {
                foreach (Address addr in this.addresses)
                {
                    if (addr != null)
                    {
                        sb.Append(addr.ToString());
                    }
                }
            }
            sb.Append("Email Addresses:");
            if (this.emailAddresses != null && this.emailAddresses.Count > 0)
            {
                foreach (EmailAddress addr in this.emailAddresses)
                {
                    if (addr != null)
                    {
                        sb.Append(addr.ToString());
                    }
                }
            }
            sb.Append("Phone Numbers:");
            if (this.phones != null && this.phones.Count > 0)
            {
                foreach (PhoneNum num in this.phones)
                {
                    if (num != null)
                    {
                        sb.Append(num.ToString());
                    }
                }
            }
            return sb.ToString();
        }
    }
}
