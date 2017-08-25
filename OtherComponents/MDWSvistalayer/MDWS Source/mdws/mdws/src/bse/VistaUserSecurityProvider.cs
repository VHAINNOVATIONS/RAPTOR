using System;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws.bse
{
    public interface IUserSecurityProvider
    {
        IPrincipal getUserPrincipal(string key);
    }

    public class VistaUserSecurityProvider : IUserSecurityProvider
    {
        public VistaUserSecurityProvider()
        {
        }

        public IPrincipal getUserPrincipal(string key)
        {
            MdwsConfiguration conf = new mdws.conf.MdwsConfiguration();
            string connectionString = conf.BseValidatorConnectionString;
            string encryptionKey = 
                conf.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.BSE_SQL_ENCRYPTION_KEY];
            IDao dao = new UserValidationDao(connectionString);
            return dao.getVisitor(key, encryptionKey).Principal;
        }
    }
}
