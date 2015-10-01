using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class EmailAddress
    {
        string username;
        string hostname;
        string addr;

        public EmailAddress() { }

        public EmailAddress(string username, string hostname)
        {
            Username = username;
            Hostname = hostname;
        }

        public EmailAddress(string emailAddress)
        {
            addr = emailAddress;
        }

        public string Address
        {
            get { return addr; }
            set { addr = value; }
        }

        public string Username
        {
            get { return username; }
            set { username = value; }
        }

        public string Hostname
        {
            get { return hostname; }
            set { hostname = value; }
        }

        internal void split(string emailAddress)
        {
            string[] parts = emailAddress.Split(new char[] { '@' });
            Username = parts[0];
            Hostname = parts[1];
        }

        public static bool isValid(string emailAddress)
        {
            if (String.IsNullOrEmpty(emailAddress))
            {
                return false;
            }
            if (emailAddress.IndexOf('@') == -1)
            {
                return false;
            }
            return true;
        }

        /// <summary>
        /// Check whether two email addresses are equal
        /// </summary>
        /// <param name="obj">EmailAddress</param>
        /// <returns>True if emails are the same (case insensitive)</returns>
        public override bool Equals(object obj)
        {
            if (!(obj is EmailAddress))
            {
                return false;
            }
            EmailAddress temp = (EmailAddress)obj;
            if (String.Equals(temp.Address, this.addr, StringComparison.CurrentCultureIgnoreCase))
            {
                return true;
            }
            return false;
        }

        /// <summary>
        /// Returns the email address string
        /// </summary>
        /// <returns>email address</returns>
        public override string ToString()
        {
            return addr ?? "";
        }

        /// <summary>
        /// Calls ToString().GetHashCode() to retrieve the hashcode of the string representation of an EmailAddress object
        /// </summary>
        /// <returns>Int32 HashCode</returns>
        public override int GetHashCode()
        {
            return this.ToString().GetHashCode();
        }
    }
}
