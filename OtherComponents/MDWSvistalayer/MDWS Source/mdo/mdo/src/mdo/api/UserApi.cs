using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo.api
{
    public class UserApi
    {
	    String DAO_NAME = "IUserDao";
    	
	    public UserApi() {}
    	
        public IndexedHashtable getUserId(ConnectionSet cxns, KeyValuePair<string, string> param)
        {
            return cxns.query(DAO_NAME, "getUserId", new object[] { param });
        }

        public string getUserId(AbstractConnection cxn, KeyValuePair<string, string> param)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).getUserId(param);
        }

        public User[] providerLookup(AbstractConnection cxn, KeyValuePair<string, string> param)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).providerLookup(param);
        }

        public IndexedHashtable providerLookup(ConnectionSet cxns, KeyValuePair<string, string> param)
        {
            return cxns.query(DAO_NAME, "providerLookup", new object[] { param });
        }

        public User[] userLookup(AbstractConnection cxn, KeyValuePair<string, string> param)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).userLookup(param);
        }

        public IndexedHashtable userLookup(ConnectionSet cxns, KeyValuePair<string, string> param)
        {
            return cxns.query(DAO_NAME, "userLookup", new object[] { param });
        }

        public User[] userLookup(AbstractConnection cxn, KeyValuePair<string, string> param, string maxrex)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).userLookup(param, maxrex);
        }

        public IndexedHashtable userLookup(ConnectionSet cxns, KeyValuePair<string, string> param, string maxrex)
        {
            return cxns.query(DAO_NAME, "userLookup", new object[] { param, maxrex });
        }

        public User getUser(AbstractConnection cxn, string userId)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).getUser(userId);
        }

        public bool hasPermission(AbstractConnection cxn, string userId, AbstractPermission permission)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).hasPermission(userId, permission);
        }

        public Dictionary<string, AbstractPermission> getPermissions(AbstractConnection cxn, string uid, PermissionType type)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).getPermissions(type, uid);
        }

        public AbstractPermission addPermission(AbstractConnection cxn, string uid, AbstractPermission permission)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).addPermission(uid, permission);
        }

        public void removePermission(AbstractConnection cxn, string uid, AbstractPermission permission)
        {
            ((IUserDao)cxn.getDao(DAO_NAME)).removePermission(uid, permission);
        }

        public bool isValidEsig(AbstractConnection cxn, string esig)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).isValidEsig(esig);
        }

        public bool isUser(AbstractConnection cxn, string uid)
        {
            return ((IUserDao)cxn.getDao(DAO_NAME)).isUser(uid);
        }
    }
}
