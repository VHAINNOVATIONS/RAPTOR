
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo.dao
{
    public interface IUserDao
    {
        string getUserId(KeyValuePair<string, string> param);
        User[] providerLookup(KeyValuePair<string, string> param);
        User[] userLookup(KeyValuePair<string, string> param);
        IList<User> userLookupList(KeyValuePair<string, string> param);
        User[] userLookup(KeyValuePair<string, string> param, string maxrex);
        User getUser(string uid);
        bool hasPermission(string uid, AbstractPermission permission);
        Dictionary<string, AbstractPermission> getPermissions(PermissionType type, string uid);
        AbstractPermission addPermission(string uid, AbstractPermission permission);
        void removePermission(string uid, AbstractPermission permission);
        bool isValidEsig(string esig);
        bool isUser(string uid);
        OrderedDictionary getUsersWithOption(string optionName);
        void updateUser(User user, string property, object value);
        void updateUser(User user, Dictionary<string, object> properties);
    }
}
