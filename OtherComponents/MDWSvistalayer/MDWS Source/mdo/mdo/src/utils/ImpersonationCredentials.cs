using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.conf
{
    public class ImpersonationCredentials
    {
        public User RunAsUser { get ; set; }

        public ImpersonationCredentials()
        {
            MdoConfiguration configuration = new MdoConfiguration(true, "secret-mdws.conf");
            RunAsUser = configuration.LdapConfiguration.RunasUser;
        }
    }
}
