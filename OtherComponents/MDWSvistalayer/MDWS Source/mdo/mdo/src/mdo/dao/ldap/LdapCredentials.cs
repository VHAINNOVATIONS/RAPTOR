using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapCredentials : AbstractCredentials
    {
        public string Domain { get; set; }

        public override bool AreTest
        {
            get { return false; }
        }

        public override bool Complete
        {
            get { throw new NotImplementedException(); }
        }
    }
}
