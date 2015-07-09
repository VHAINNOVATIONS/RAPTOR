using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.sm
{
    public class SmConnection : AbstractConnection
    {
        public SmConnection(DataSource src)
            : base(src)
        {
            if (String.IsNullOrEmpty(src.ConnectionString))
            {
                throw new mdo.exceptions.MdoException(mdo.exceptions.MdoExceptionCode.DATA_SOURCE_MISSING_CXN_STRING, "Must supply SM service endpoint");
            }
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            throw new NotImplementedException();
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
            throw new NotImplementedException();
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
