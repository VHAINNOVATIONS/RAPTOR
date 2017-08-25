using System;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.Web;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.utils;
using gov.va.medora.mdo.api;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class UserLib
    {
        MySession mySession;

        public UserLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public UserTO userLookup(string duz)
        {
            UserTO result = new UserTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);

            if (String.IsNullOrEmpty(duz))
            {
                result.fault = new FaultTO("Missing DUZ param");
            }
            else if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.BaseConnection;
                UserApi api = new UserApi();
                User[] user = api.userLookup(cxn, new System.Collections.Generic.KeyValuePair<string, string>("DUZ", duz));
                result = new UserTO(user[0]);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public UserArray lookup(string target, string maxRex)
        {
            return lookup(null, target, maxRex);
        }

        public UserArray lookup(string sitecode, string target, string maxRex)
        {
            UserArray result = new UserArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (target == "")
            {
                result.fault = new FaultTO("Missing target");
            }
            else if (!StringUtils.isNumeric(maxRex))
            {
                result.fault = new FaultTO("Non-numeric maxRex");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(sitecode))
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.Connections[sitecode];
                UserApi api = new UserApi();
                User[] matches = api.userLookup(cxn, new KeyValuePair<string,string>("NAME", target)); //.userLookupByName(cxn, target, maxRex);
                result = new UserArray(matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        // DON'T SEE A USERLOOKUPBYNAME FUNCTION ON USERAPI
        public TaggedUserArrays lookupMS(string target, string maxRex)
        {
            TaggedUserArrays result = new TaggedUserArrays();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (target == "")
            {
                result.fault = new FaultTO("Missing target");
            }
            //else if (!StringUtils.isNumeric(maxRex))
            //{
            //    result.fault = new FaultTO("Non-numeric maxRex");
            //}
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                KeyValuePair<string, string> kvp = new KeyValuePair<string, string>("NAME",target);
                UserApi api = new UserApi();
                IndexedHashtable t = api.userLookup(mySession.ConnectionSet, kvp, maxRex);
                result = new TaggedUserArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public UserTO getUser(string DUZ)
        {
            return getUser(mySession.ConnectionSet.BaseConnection, DUZ);
        }

        public UserTO getUser(string sitecode, string DUZ)
        {
            if (sitecode == "")
            {
                UserTO result = new UserTO();
                result.fault = new FaultTO("Missing sitecode");
                return result;
            }
            return getUser(mySession.ConnectionSet.getConnection(sitecode), DUZ);
        }

        internal UserTO getUser(AbstractConnection cxn, string DUZ)
        {
            UserTO result = new UserTO();
            if (DUZ == "")
            {
                result.fault = new FaultTO("Missing DUZ");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                UserApi api = new UserApi();
                User u = api.getUser(cxn, DUZ);
                result = new UserTO(u);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public UserArray cprsUserLookup(string target)
        {
            return cprsUserLookup(null, target);
        }

        // 
        public UserArray cprsUserLookup(string sitecode, string target)
        {
            UserArray result = new UserArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(sitecode))
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                UserApi api = new UserApi();
                User[] matches = api.providerLookup(cxn, new KeyValuePair<string,string>("NAME",target));
                result = new UserArray(matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getUserIdBySSN(string SSN)
        {
            TaggedTextArray result = new TaggedTextArray();
            if (SSN == "")
            {
                result.fault = new FaultTO("Missing SSN");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                UserApi api = new UserApi();
                IndexedHashtable t = api.getUserId(mySession.ConnectionSet, new System.Collections.Generic.KeyValuePair<string, string>("SSN", SSN));
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        //public UserTO getUserInfo(string DUZ)
        //{
        //    return getUserInfo(mySession.cxnMgr.LoginConnection, DUZ);
        //}

        //public UserTO getUserInfo(string sitecode, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        UserTO result = new UserTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return getUserInfo(mySession.cxns.getConnection(sitecode), DUZ);
        //}

        //internal UserTO getUserInfo(AbstractConnection cxn, string DUZ)
        //{
        //    UserTO result = new UserTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        User u = api.getUserInfo(cxn, DUZ);
        //        result = new UserTO(u);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public UserSecurityKeyArray getSecurityKeys(string DUZ)
        //{
        //    return getSecurityKeys(mySession.cxnMgr.LoginConnection, DUZ);
        //}

        //public UserSecurityKeyArray getSecurityKeys(string sitecode, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        UserSecurityKeyArray result = new UserSecurityKeyArray();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return getSecurityKeys(mySession.cxns.getConnection(sitecode), DUZ);
        //}

        //internal UserSecurityKeyArray getSecurityKeys(AbstractConnection cxn, string DUZ)
        //{
        //    UserSecurityKeyArray result = new UserSecurityKeyArray();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        UserSecurityKey[] keys = api.getSecurityKeys(cxn, DUZ);
        //        result = new UserSecurityKeyArray(keys);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO addSecurityKeyForContext(string DUZ, string context)
        //{
        //    return addSecurityKeyForContext(mySession.cxnMgr.LoginConnection, DUZ, context);
        //}

        //public TextTO addSecurityKeyForContext(string sitecode, string DUZ, string context)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return addSecurityKeyForContext(mySession.cxns.getConnection(sitecode), DUZ, context);
        //}

        //internal TextTO addSecurityKeyForContext(Connection cxn, string DUZ, string context)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (context == "")
        //    {
        //        result.fault = new FaultTO("Missing context");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        string s = api.addSecurityKeyForContext(cxn, DUZ, context);
        //        result = new TextTO(s);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO addSecurityKey(string DUZ, string securityKey)
        //{
        //    return addSecurityKey(mySession.cxnMgr.LoginConnection, DUZ, securityKey);
        //}

        //public TextTO addSecurityKey(string sitecode, string DUZ, string securityKey)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return addSecurityKey(mySession.cxns.getConnection(sitecode), DUZ, securityKey);
        //}

        //internal TextTO addSecurityKey(Connection cxn, string DUZ, string securityKey)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (securityKey == "")
        //    {
        //        result.fault = new FaultTO("Missing security key");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        string s = api.addSecurityKey(cxn, DUZ, securityKey);
        //        result = new TextTO(s);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO removeSecurityKey(string DUZ, string securityKey)
        //{
        //    return removeSecurityKey(mySession.cxnMgr.LoginConnection, DUZ, securityKey);
        //}

        //public TextTO removeSecurityKey(string sitecode, string DUZ, string securityKey)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return removeSecurityKey(mySession.cxns.getConnection(sitecode), DUZ, securityKey);
        //}

        //internal TextTO removeSecurityKey(Connection cxn, string DUZ, string securityKey)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (securityKey == "")
        //    {
        //        result.fault = new FaultTO("Missing security key");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        string s = api.removeSecurityKey(cxn, DUZ, securityKey);
        //        result = new TextTO(s);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public UserOptionArray getMenuOptions(string DUZ)
        //{
        //    return getMenuOptions(mySession.cxnMgr.LoginConnection, DUZ);
        //}

        //public UserOptionArray getMenuOptions(string sitecode, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        UserOptionArray result = new UserOptionArray();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return getMenuOptions(mySession.cxns.getConnection(sitecode), DUZ);
        //}

        //internal UserOptionArray getMenuOptions(Connection cxn, string DUZ)
        //{
        //    UserOptionArray result = new UserOptionArray();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        UserOption[] o = api.getMenuOptions(cxn, DUZ);
        //        result = new UserOptionArray(o);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public UserOptionArray getDelegatedOptions(string DUZ)
        //{
        //    return getDelegatedOptions(mySession.cxnMgr.LoginConnection, DUZ);
        //}

        //public UserOptionArray getDelegatedOptions(string sitecode, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        UserOptionArray result = new UserOptionArray();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return getDelegatedOptions(mySession.cxns.getConnection(sitecode), DUZ);
        //}

        //internal UserOptionArray getDelegatedOptions(Connection cxn, string DUZ)
        //{
        //    UserOptionArray result = new UserOptionArray();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        UserOption[] o = api.getDelegatedOptions(cxn, DUZ);
        //        result = new UserOptionArray(o);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO addMenuOption(string context, string DUZ)
        //{
        //    return addMenuOption(mySession.cxnMgr.LoginConnection, context, DUZ);
        //}

        //public TextTO addMenuOption(string sitecode, string context, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return addMenuOption(mySession.cxns.getConnection(sitecode), context, DUZ);
        //}

        //internal TextTO addMenuOption(Connection cxn, string context, string DUZ)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (context == "")
        //    {
        //        result.fault = new FaultTO("Missing context");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        string s = api.addMenuOption(cxn, context, DUZ);
        //        result = new TextTO(s);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO addDelegatedOption(string context, string DUZ)
        //{
        //    return addDelegatedOption(mySession.cxnMgr.LoginConnection, context, DUZ);
        //}

        //public TextTO addDelegatedOption(string sitecode, string context, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return addDelegatedOption(mySession.cxns.getConnection(sitecode), context, DUZ);
        //}

        //internal TextTO addDelegatedOption(Connection cxn, string context, string DUZ)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (context == "")
        //    {
        //        result.fault = new FaultTO("Missing context");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        string s = api.addDelegatedOption(cxn, context, DUZ);
        //        result = new TextTO(s);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO removeMenuOption(string optNum, string DUZ)
        //{
        //    return removeMenuOption(mySession.cxnMgr.LoginConnection, optNum, DUZ);
        //}

        //public TextTO removeMenuOption(string sitecode, string optNum, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return removeMenuOption(mySession.cxns.getConnection(sitecode), optNum, DUZ);
        //}

        //internal TextTO removeMenuOption(Connection cxn, string optNum, string DUZ)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (optNum == "")
        //    {
        //        result.fault = new FaultTO("Missing optNum");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        api.removeMenuOption(cxn, optNum, DUZ);
        //        result = new TextTO("OK");
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        //public TextTO removeDelegatedOption(string optNum, string DUZ)
        //{
        //    return removeDelegatedOption(mySession.cxnMgr.LoginConnection, optNum, DUZ);
        //}

        //public TextTO removeDelegatedOption(string sitecode, string optNum, string DUZ)
        //{
        //    if (sitecode == "")
        //    {
        //        TextTO result = new TextTO();
        //        result.fault = new FaultTO("Missing sitecode");
        //        return result;
        //    }
        //    return removeDelegatedOption(mySession.cxns.getConnection(sitecode), optNum, DUZ);
        //}

        //internal TextTO removeDelegatedOption(Connection cxn, string optNum, string DUZ)
        //{
        //    TextTO result = new TextTO();
        //    if (DUZ == "")
        //    {
        //        result.fault = new FaultTO("Missing DUZ");
        //    }
        //    else if (optNum == "")
        //    {
        //        result.fault = new FaultTO("Missing optNum");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        UserApi api = new UserApi();
        //        api.removeDelegatedOption(cxn, optNum, DUZ);
        //        result = new TextTO("OK");
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        public TextTO isValidEsig(string esig)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (String.IsNullOrEmpty(esig))
            {
                result.fault = new FaultTO("No E-Signature provided.", "Need to provide an E-Signature code");
            }
            if (result.fault != null)
            {
                return result;
            }
            return isValidEsig(mySession.ConnectionSet.BaseConnection, esig);
        }

        public TextTO isValidEsig(string sitecode, string esig)
        {
            if (sitecode == "")
            {
                TextTO result = new TextTO();
                result.fault = new FaultTO("Missing sitecode");
                return result;
            }
            return isValidEsig(mySession.ConnectionSet.getConnection(sitecode), esig);
        }

        internal TextTO isValidEsig(AbstractConnection cxn, string esig)
        {
            TextTO result = new TextTO();
            if (esig == "")
            {
                result.fault = new FaultTO("Missing esig");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                UserApi api = new UserApi();
                bool f = api.isValidEsig(cxn, esig);
                result = new TextTO((f ? "TRUE" : "FALSE"));
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getUsersWithOption(string optionName)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(optionName))
            {
                result.fault = new FaultTO("Empty Option Name");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                OrderedDictionary d = User.getUsersWithOption(mySession.ConnectionSet.BaseConnection, optionName);
                result = new TaggedTextArray(d);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public BoolTO hasPermission(string uid, string permissionName)
        {
            BoolTO result = new BoolTO();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(uid))
            {
                result.fault = new FaultTO("Empty UID");
            }
            else if (String.IsNullOrEmpty(permissionName))
            {
                result.fault = new FaultTO("Empty permission name");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                AbstractPermission p = new gov.va.medora.mdo.dao.vista.MenuOption(permissionName);
                bool f = User.hasPermission(mySession.ConnectionSet.BaseConnection, uid, p);
                result = new BoolTO(f);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public UserSecurityKeyArray getUserSecurityKeys(string uid)
        {
            UserSecurityKeyArray result = new UserSecurityKeyArray();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(uid))
            {
                result.fault = new FaultTO("Empty UID");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Dictionary<string, AbstractPermission> d = User.getPermissions(mySession.ConnectionSet.BaseConnection, uid, PermissionType.SecurityKey);
                result = new UserSecurityKeyArray(d);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

    }
}
