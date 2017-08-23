using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.ldap
{
    public class ActiveDirectoryConstants
    {
        public const string USERNAME = "samaccountname";
        public const string FIRSTNAME = "givenname";
        public const string LASTNAME = "sn";
        public const string TELEPHONENUMBER = "telephonenumber";
        public const string ADDRESS = "streetaddress";
        public const string CITY = "l";
        public const string STATE = "st";
        public const string ZIP = "postalcode";
        public const string TITLE = "title";
        public const string DESCRIPTION = "description";
        public const string OFFICE = "physicaldeliveryofficename";
        public const string DEPARTMENT = "department";
        public const string FAX = "facsimileTelephoneNumber";
        public const string MODIFIED = "whenChanged";
    }
}
