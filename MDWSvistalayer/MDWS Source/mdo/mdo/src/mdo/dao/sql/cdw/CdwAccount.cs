using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.conf;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwAccount : AbstractAccount
    {
        private string Mode { get; set; }

        public CdwAccount(AbstractConnection cxn) : base(cxn) {
            //MdoConfiguration configuration = new MdoConfiguration();
            //Mode = configuration.ImpersonationCredentials.Mode;
            Mode = "dev";
        }

        public override string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null)
        {
            throw new NotImplementedException("Vista Authentication should be used instead."); 
        }

        public override User authorize(AbstractCredentials credentials, AbstractPermission permission)
        { 
            throw new NotImplementedException("Vista Authentication should be used instead."); 
        }

        public override User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            if ("prod".Equals(Mode))
            {
                AbstractConnection vistaConnection = initializeVistaConnection();
                gov.va.medora.mdo.dao.vista.VistaAccount vistaAccount = new vista.VistaAccount(vistaConnection);
                vistaAccount.AuthenticationMethod = this.AuthenticationMethod;
                vistaConnection.Account = vistaAccount;
                User user = vistaAccount.authenticateAndAuthorize(credentials, permission, vistaConnection.DataSource);

                this.isAuthenticated = vistaAccount.IsAuthenticated;
                this.isAuthorized = vistaAccount.IsAuthorized;

                return user;
            }
            else
            {
                this.isAuthenticated = true;
                this.isAuthorized = true;

                User user = new User();
                user.setName("dev");

                return user;
            }
        }

        internal void validateCredentials(AbstractCredentials credentials)
        {
            if (Cxn == null || !Cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Must have connection");
            }
            if (String.IsNullOrEmpty(credentials.AccountName))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing Access Code");
            }
            if (String.IsNullOrEmpty(credentials.AccountPassword))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing Verify Code");
            }
        }

        internal AbstractConnection initializeVistaConnection()
        {
            DataSource vistaDataSource = new DataSource();
            vistaDataSource.Provider = Cxn.DataSource.Provider;
            vistaDataSource.Port = Cxn.DataSource.Port;
            vistaDataSource.SiteId = new SiteId("CDW");

            // Use the factory to ensure that the vista connection is initialized with the right Factory-defined Connection Strategy
            gov.va.medora.mdo.dao.vista.VistaDaoFactory factory = (vista.VistaDaoFactory) AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.VISTA);
            gov.va.medora.mdo.dao.vista.VistaConnection vistaConnection = (vista.VistaConnection) factory.getConnection(vistaDataSource);
            
            vistaConnection.connect();

            return vistaConnection;
        }


    }
}
