using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieUserDao : IUserDao
    {
        VistaUserDao vistaDao = null;

        public FhieUserDao(AbstractConnection cxn)
        {
            vistaDao = new VistaUserDao(cxn);
        }

        public string getUserId(KeyValuePair<string, string> param)
        {
            return vistaDao.getUserId(param);
        }

        public User[] providerLookup(KeyValuePair<string, string> param)
        {
            return vistaDao.providerLookup(param);
        }

        public User[] userLookup(KeyValuePair<string, string> param)
        {
            return vistaDao.userLookup(param);
        }

        public User[] userLookup(KeyValuePair<string, string> param, string maxrex)
        {
            return vistaDao.userLookup(param, maxrex);
        }

        public User getUser(string duz)
        {
            return vistaDao.getUser(duz);
        }

        public bool hasPermission(string duz, AbstractPermission permission)
        {
            return vistaDao.hasPermission(duz,permission);
        }

        public Dictionary<string, AbstractPermission> getPermissions(PermissionType type, string duz)
        {
            return vistaDao.getPermissions(type, duz);
        }

        public AbstractPermission addPermission(string duz, AbstractPermission permission)
        {
            return vistaDao.addPermission(duz, permission);
        }

        public void removePermission(string duz, AbstractPermission permission)
        {
            removePermission(duz,permission);
        }

        public bool isValidEsig(string esig)
        {
            return vistaDao.isValidEsig(esig);
        }

        public bool isUser(string duz)
        {
            return vistaDao.isUser(duz);
        }

        public OrderedDictionary getUsersWithOption(string optionName)
        {
            return null;
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
