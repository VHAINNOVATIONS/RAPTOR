using System;
using System.Data;
using System.Configuration;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdws.dto
{
    public class UserSecurityKeyTO : AbstractTO
    {
        public string id = "";
        public string name = "";
        public string descriptiveName = "";
        public string creatorId = "";
        public string creatorName = "";
        public string creationDate = "";
        public string reviewDate = "";

        public UserSecurityKeyTO() { }

        public UserSecurityKeyTO(UserSecurityKey mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.descriptiveName = mdo.DescriptiveName;
            this.creatorId = mdo.CreatorId;
            this.creatorName = mdo.CreatorName;
            this.creationDate = mdo.CreationDate.Year == 1 ? "" : mdo.CreationDate.ToString("yyyyMMdd.HHmmss");
            this.reviewDate = mdo.ReviewDate.Year == 1 ? "" : mdo.ReviewDate.ToString("yyyyMMdd.HHmmss");
        }

        public UserSecurityKeyTO(AbstractPermission p)
        {
            if (p.Type != PermissionType.SecurityKey)
            {
                fault = new FaultTO(p.Name + " is not a Security Key");
                return;
            }
            this.id = p.PermissionId;
            this.name = p.Name;
        }
    }
}
