using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class SecurityKey : AbstractPermission
    {
        public SecurityKey() : base() { }
        public SecurityKey(string keyId, string name) : base(keyId, name) { }
        public SecurityKey(string keyId, string name, string recordId) : base(keyId, name, recordId) { }

        public override PermissionType Type
        {
            get { return PermissionType.SecurityKey; }
        }
    }
}
