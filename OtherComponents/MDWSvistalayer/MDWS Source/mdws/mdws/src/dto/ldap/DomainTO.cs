using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.DirectoryServices.ActiveDirectory;

namespace gov.va.medora.mdws.dto.ldap
{
    [Serializable]
    public class DomainTO : AbstractTO
    {
        public string simpleName;
        public string name;
        public string distinguishedName;
        public DomainTO() { }


        public DomainTO(Domain domain)
        {
            if (domain == null)
            {
                return;
            }

            name = domain.Name;
            distinguishedName = gov.va.medora.mdo.dao.ldap.LdapUtils.getDistinguishedName(name);
        }
    }
}