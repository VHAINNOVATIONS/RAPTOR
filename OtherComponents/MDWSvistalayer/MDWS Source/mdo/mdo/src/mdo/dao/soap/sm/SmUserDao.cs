using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.sm.query;
using gov.va.medora.mdo.sm.admin;

namespace gov.va.medora.mdo.dao.soap.sm
{
    public class SmUserDao : IUserDao
    {
        AbstractConnection _cxn;
        QueryService _querySvc;
        AdminQueries _adminSvc;
        // AdminQueries exposes the following calls:
        //_adminSvc.getClinicsByName();
        //_adminSvc.getPatientsByClinicAndDate();
        //_adminSvc.getPatientsByProvider();
        //_adminSvc.getPatientsByTeam();
        //_adminSvc.getProvidersByNameAndDUZ();
        //_adminSvc.getRelationshipsByPatientAndDt();
        //_adminSvc.getTeamsByName();
        //_adminSvc.getUserByNameAndDUZ(); <-- Is this a dupe of QueryService.getUserDemographics ??


        public SmUserDao(AbstractConnection cxn)
        {
            if (cxn == null || cxn.DataSource == null || String.IsNullOrEmpty(cxn.DataSource.ConnectionString))
            {
                throw new mdo.exceptions.MdoException(mdo.exceptions.MdoExceptionCode.DATA_SOURCE_MISSING_CXN_STRING, "Must supply SM service endpoint");
            }
            _cxn = cxn;
            _querySvc = new QueryService();
            _querySvc.Url = _cxn.DataSource.ConnectionString;

            // TBD - is MHV using AdminService for anything??
            //_adminSvc = new AdminQueries();
            //_adminSvc.Url = cxn.DataSource.ConnectionString;
        }


        /// <summary>
        /// SM messaging call to getUserDemographics
        /// </summary>
        /// <param name="lastName"></param>
        /// <param name="firstName"></param>
        /// <param name="userId"></param>
        /// <param name="sitecode"></param>
        /// <returns></returns>
        public IList<User> userLookup(string lastName, string firstName, string userId, string sitecode)
        {
            mdo.sm.query.UserLookupResponse response = _querySvc.getUserDemographics(lastName, firstName, userId, sitecode);
            
            if (response == null || response.Users == null || response.Users.Length == 0)
            {
                return null;
            }

            IList<User> result = new List<User>();

            foreach (gov.va.medora.mdo.sm.query.User user in response.Users)
            {
                User newUser = new User();
                newUser.Service = new Service() { Name = user.Department };
                newUser.Name = new PersonName() { Firstname = user.FirstName, Lastname = user.LastName };
                newUser.Id = user.IEN;
                newUser.Phone = user.Phone;
                user.ProviderIndicator = user.ProviderIndicator;
                user.SSN = user.SSN;
                user.Title = user.Title;

                result.Add(newUser);
            }

            return result;
        }



        #region Not Implemented
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

        public string getUserId(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }

        public User[] providerLookup(KeyValuePair<string, string> param)
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
        #endregion



        public void updateUser(User user, string property, object value)
        {
            throw new NotImplementedException();
        }

        public void updateUser(User user, Dictionary<string, object> properties)
        {
            throw new NotImplementedException();
        }
    }
}
