using System;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class MockAccount : VistaAccount
    {
        bool _authenticate = true;

        public MockAccount(AbstractConnection cxn) : base(cxn) { }

        public MockAccount(AbstractConnection cxn, bool authenticate)
            : base(cxn)
        {
            _authenticate = authenticate;
        }

        public override string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null)
        {
            if (_authenticate)
            {
                return base.authenticate(credentials, validationDataSource);
            }
            
            isAuthorized = isAuthenticated = true;
            return "OK";
            //if (Cxn == null || !Cxn.IsConnected)
            //{
            //    throw new ConnectionException("Must have connection");
            //}
            //if (credentials == null)
            //{
            //    throw new ArgumentNullException("credentials");
            //}
            //else
            //{
            //    throw new ArgumentException("Invalid credentials");
            //}
        }

        public override User authorize(AbstractCredentials credentials, AbstractPermission permission)
        {
            if (_authenticate)
            {
                return base.authorize(credentials, permission);
            }

            isAuthorized = isAuthenticated = true;
            return new User();
        }

        public override User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            if (_authenticate)
            {
                return base.authenticateAndAuthorize(credentials, permission, validationDataSource);
            }

            isAuthorized = isAuthenticated = true;
            return new User();
        }

        public string login(AbstractCredentials credentials)
        {
            if (_authenticate)
            {
                return base.login(credentials);
            }

            isAuthorized = isAuthenticated = true;
            return "OK";
        }
    }
}
