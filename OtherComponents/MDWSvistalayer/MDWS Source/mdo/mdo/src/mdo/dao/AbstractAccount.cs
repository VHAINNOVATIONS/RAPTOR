using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public abstract class AbstractAccount
    {
        string id;
        AbstractConnection cxn;
        Dictionary<string, AbstractPermission> permissions = new Dictionary<string, AbstractPermission>();
        protected bool isAuthenticated;
        protected bool isAuthorized;
        string authMethod;

        public AbstractAccount() { }

        public AbstractAccount(AbstractConnection cxn)
        {
            this.cxn = cxn;
            isAuthenticated = false;
            isAuthorized = false;
        }

        public string AccountId
        {
            get { return id; }
            set { id = value; }
        }

        public AbstractConnection Cxn
        {
            get { return cxn; }
            set { cxn = value; }
        }

        public Dictionary<string, AbstractPermission> Permissions
        {
            get { return permissions; }
            set { permissions = value; }
        }

        public bool IsAuthenticated
        {
            get { return isAuthenticated; }
            set { isAuthenticated = value; }
        }

        public bool IsAuthorized
        {
            get { return isAuthorized; }
        }

        public string AuthenticationMethod
        {
            get { return authMethod; }
            set { authMethod = value; }
        }

        public AbstractPermission PrimaryPermission
        {
            get
            {
                if (permissions == null || permissions.Count == 0)
                {
                    return null;
                }
                if (permissions.Count == 1)
                {
                    string[] key = new string[1];
                    permissions.Keys.CopyTo(key, 0);
                    return (AbstractPermission)permissions[key[0]];
                }
                foreach (KeyValuePair<string, AbstractPermission> kvp in permissions)
                {
                    AbstractPermission p = (AbstractPermission)kvp.Value;
                    if (p.IsPrimary)
                    {
                        return p;
                    }
                }
                return null;
            }
        }

        public abstract string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null);
        public abstract User authorize(AbstractCredentials credentials, AbstractPermission permission);
        public abstract User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null);
    }
}
