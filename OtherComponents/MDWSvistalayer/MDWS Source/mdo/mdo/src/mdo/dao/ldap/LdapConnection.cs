using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.DirectoryServices;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapConnection : AbstractConnection
    {
        public LdapConnection(DataSource src) : base(src) { }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            IsConnected = true;
        }

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            throw new NotImplementedException();
        }

        public override string getWelcomeMessage()
        {
            throw new NotImplementedException();
        }

        public override bool hasPatch(string patchId)
        {
            throw new NotImplementedException();
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            if (this.DataSource == null || String.IsNullOrEmpty(this.DataSource.Provider))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Invalid domain");
            }
            DirectoryEntry de = new DirectoryEntry(this.DataSource.Provider);
            de.AuthenticationType = AuthenticationTypes.Secure;
            DirectorySearcher search = new DirectorySearcher();
            search.SearchRoot = de;
            search.Filter = request;
            return search.FindAll();
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override string getServerTimeout()
        {
            throw new NotImplementedException();
        }

        public override void disconnect()
        {
            IsConnected = false;
        }

        public override Dictionary<string, object> getState()
        {
            throw new NotImplementedException();
        }

        public override void setState(Dictionary<string, object> session)
        {
            throw new NotImplementedException();
        }

        public override bool isAlive()
        {
            throw new NotImplementedException();
        }
    }
}
