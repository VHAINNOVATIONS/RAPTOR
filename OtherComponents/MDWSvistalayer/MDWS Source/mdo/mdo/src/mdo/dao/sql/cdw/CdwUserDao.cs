using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwUserDao : IUserDao
    {

        public string getUserId(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }

        public User[] providerLookup(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }

        public User[] userLookup(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }

        public User[] userLookup(KeyValuePair<string, string> param, string maxrex)
        {
            throw new NotImplementedException();
        }

        public User getUser(string uid)
        {
            throw new NotImplementedException();
        }

        public bool hasPermission(string uid, AbstractPermission permission)
        {
            throw new NotImplementedException();
        }

        public Dictionary<string, AbstractPermission> getPermissions(PermissionType type, string uid)
        {
            throw new NotImplementedException();
        }

        public AbstractPermission addPermission(string uid, AbstractPermission permission)
        {
            throw new NotImplementedException();
        }

        public void removePermission(string uid, AbstractPermission permission)
        {
            throw new NotImplementedException();
        }

        public bool isValidEsig(string esig)
        {
            throw new NotImplementedException();
        }

        public bool isUser(string uid)
        {
            throw new NotImplementedException();
        }

        public System.Collections.Specialized.OrderedDictionary getUsersWithOption(string optionName)
        {
            throw new NotImplementedException();
        }


        public void updateUser(User user, string property, object value)
        {
            throw new NotImplementedException();
        }

        public void updateUser(User user, Dictionary<string, object> properties)
        {
            throw new NotImplementedException();
        }


        public IList<User> userLookupList(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }
    }
}
