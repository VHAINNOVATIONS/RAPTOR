using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapGroup : AbstractPermission
    {

        public override PermissionType Type
        {
            get { return PermissionType.LdapGroup; }
        }
    }
}
