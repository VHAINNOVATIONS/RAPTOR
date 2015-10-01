using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;
using System.DirectoryServices;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapUserDao : IUserDao
    {
        LdapConnection _cxn;

        public LdapUserDao(AbstractConnection cxn)
        {
            if (!(cxn is LdapConnection))
            {
                throw new MdoException("Connection must be LdapConnection");
            }

            _cxn = (LdapConnection)cxn;
        }

        public string getUserId(KeyValuePair<string, string> param)
        {
            string request = buildGetUserIdRequest(param);
            SearchResultCollection result = (SearchResultCollection)_cxn.query(request);
            return result[0].GetDirectoryEntry().Guid.ToString();
        }

        internal string buildGetUserIdRequest(KeyValuePair<string, string> param)
        {
            return "(&(objectClass=user)(SAMAccountName=" + param.Value + "))";
        }

        internal void update(User runAs, DirectoryEntry entry, string key, string value)
        {
            // needed because of some weird issue with setting a value to an empty string without calling the remove function first...
            if (String.IsNullOrEmpty(value))
            {
                entry.Properties[key].Value = "workaround";
                entry.Properties[key].Remove("workaround");
            }
            else
            {
                entry.Properties[key].Value = value;
            }


            entry.CommitChanges();
            entry.RefreshCache();
        }

        internal SearchResultCollection getUserByGuid(string guid)
        {
            Guid objGuid = new Guid(guid);
            byte[] byteGuid = objGuid.ToByteArray();
            StringBuilder sb = new StringBuilder();
            foreach (byte b in byteGuid)
            {
                sb.Append(@"\" + b.ToString("x2"));
            }
            SearchResultCollection result = (SearchResultCollection)_cxn.query("(&(objectClass=user)(objectGUID=" + sb.ToString() + "))");
            return result;
        }

        internal SearchResultCollection getUserByUsername(string username)
        {
            string request = buildGetUserIdRequest(new KeyValuePair<string, string>("", username));
            SearchResultCollection result = (SearchResultCollection)_cxn.query(request);
            return result;
        }

        private SearchResultCollection getUserByEmailAddress(string uid)
        {
            SearchResultCollection result = (SearchResultCollection)_cxn.query("(&(objectClass=user)(mail=" + uid + "))");
            return result;
        }

        /// <summary>
        /// Get a user by id. ID can be the user's LDAP username, email address or GUID
        /// </summary>
        /// <param name="uid">Examples: vhaannmewtoj, joel.mewton@va.gov, 2ea93ce5-95ae-4a19-80b0-b895dcfb879e</param>
        /// <returns><![CDATA[IList<User>]]></returns>
        public IList<User> userLookupList(KeyValuePair<string, string> param)
        {
            Guid trash = new Guid();
            SearchResultCollection src = null;

            if (Guid.TryParse(param.Value, out trash))
            {
                src = getUserByGuid(param.Value);
            }
            else if (!String.IsNullOrEmpty(param.Value) && param.Value.Contains("@")) // email address?? -> something@va.gov
            {
                src = getUserByEmailAddress(param.Value);
            }
            else
            {
                src = getUserByUsername(param.Value);
            }

            return toUsersFromSearchResultCollection(src);
        }

        internal IList<User> toUsersFromSearchResultCollection(SearchResultCollection src)
        {
            IList<User> results = new List<User>();

            if (src == null || src.Count == 0)
            {
                return results;
            }

            for (int i = 0; i < src.Count; i++)
            {
                results.Add(toUserFromDirectoryEntry(src[i].GetDirectoryEntry()));
            }

            return results;
        }

        internal User toUserFromDirectoryEntry(DirectoryEntry de)
        {
            User result = new User();
            if (de == null || de.Properties == null)
            {
                return result;
            }

            DirectoryEntry root = de;
            while (true)
            {
                if (String.Equals(root.SchemaClassName, "domainDNS", StringComparison.CurrentCultureIgnoreCase))
                {
                    result.Domain = String.Concat("LDAP://", tryGetProperty(root, "distinguishedName"));
                    //foreach (String key in root.Properties.PropertyNames)
                    //{
                    //    System.Console.WriteLine("{0} - {1}", key, root.Properties[key][0]);
                    //}
                    break;
                }
                root = root.Parent;
            }

            result.Title = tryGetProperty(de, "title");
            result.Name = new PersonName();
            result.Name.Lastname = tryGetProperty(de, "sn");
            result.Name.Firstname = tryGetProperty(de, "givenName");
            result.EmailAddress = tryGetProperty(de, "mail");
            result.UserName = tryGetProperty(de, "sAMAccountName");
            result.Phone = tryGetProperty(de, "telephoneNumber");
            result.Phone = tryGetProperty(de, "telephoneNumber");
            result.Demographics = new Dictionary<string, DemographicSet>();
            result.Demographics.Add("LDAP", new DemographicSet());
            PhoneNum officePhone = new PhoneNum(result.Phone);
            result.Demographics["LDAP"].PhoneNumbers = new List<PhoneNum>() { officePhone };
            Address address = new Address()
            {
                Street1 = tryGetProperty(de, "streetAddress"),
                City = tryGetProperty(de, "l"),
                State = tryGetProperty(de, "st"),
                Zipcode = tryGetProperty(de, "postalCode")
            };
            result.Demographics["LDAP"].StreetAddresses = new List<Address>() { address };
            result.Demographics["LDAP"].EmailAddresses = new List<EmailAddress>() { new EmailAddress(result.EmailAddress) };
            result.Uid = de.Guid.ToString();
            return result;
        }

        String tryGetProperty(DirectoryEntry entry, String propertyName)
        {
            foreach (String propName in entry.Properties.PropertyNames)
            {
                System.Console.WriteLine(propName + " - " + entry.Properties[propName].Value);
            }
            if (entry.Properties.Contains(propertyName))
            {
                return entry.Properties[propertyName][0] as String;
            }
            return String.Empty;
        }

        #region Not Implemented Members
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

        User IUserDao.getUser(string uid)
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
        #endregion

    }
}
