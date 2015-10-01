using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class EmailAddressTO
    {
        public string username;
        public string hostname;
        public string addr;

        public EmailAddressTO() { }

        public EmailAddressTO(EmailAddress mdo)
        {
            this.username = mdo.Username;
            this.hostname = mdo.Hostname;
            this.addr = mdo.Address;
        }
    }
}