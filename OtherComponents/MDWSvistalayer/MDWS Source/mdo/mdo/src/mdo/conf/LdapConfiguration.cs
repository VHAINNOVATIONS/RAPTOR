using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.conf
{
    public class LdapConfiguration
    {
        public User RunasUser { get; set; }

        public LdapConfiguration() { }

        public LdapConfiguration(Dictionary<string, string> settings)
        {
            if (settings.ContainsKey(ConfigFileConstants.RUNAS_USER_DOMAIN))
            {
                if (this.RunasUser == null)
                {
                    this.RunasUser = new User();
                }
                this.RunasUser.Domain = settings[ConfigFileConstants.RUNAS_USER_DOMAIN];
            }
            if (settings.ContainsKey(ConfigFileConstants.RUNAS_USER_NAME))
            {
                if (this.RunasUser == null)
                {
                    this.RunasUser = new User();
                }
                this.RunasUser.UserName = settings[ConfigFileConstants.RUNAS_USER_NAME];
            }
            if (settings.ContainsKey(ConfigFileConstants.RUNAS_USER_PASSWORD))
            {
                if (this.RunasUser == null)
                {
                    this.RunasUser = new User();
                }
                this.RunasUser.Pwd = settings[ConfigFileConstants.RUNAS_USER_PASSWORD];
            }
        }
    }
}
