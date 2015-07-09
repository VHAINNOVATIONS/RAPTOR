using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwConnection : AbstractConnection
    {
        public RdwConnection(DataSource src) : base(src) 
        {
            this.Account = new RdwAccount(this);
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            Uri temp = null;
            if (Uri.TryCreate(this.DataSource.Provider, UriKind.Absolute, out temp))
            {
                Dns.GetHostAddresses(temp.DnsSafeHost);
                IsConnected = true;
            }
            else
            {
                throw new MdoException("Invalid RDW DataSource provider");
            }
        }

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            throw new NotImplementedException();
        }

        public override string getWelcomeMessage()
        {
            return "Successfully resolved RDW DataSource";
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
            //MDWSRPC proxy = new MDWSRPC();
            //proxy.Url = this.DataSource.Provider;
            throw new NotImplementedException();
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
    }
}
