using System;
using System.Collections.Generic;
using System.DirectoryServices;
using System.DirectoryServices.AccountManagement;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapAccount : AbstractAccount
    {
        public LdapAccount(AbstractConnection cxn) : base(cxn) { }

        public override string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null)
        {
            if (!(credentials is LdapCredentials))
            {
                throw new MdoException("Invalid credentials - not LDAP credentials");
            }
            LdapCredentials ldapCreds = (LdapCredentials)credentials;
            
            DirectoryEntry entry = new DirectoryEntry(Cxn.DataSource.Provider, ldapCreds.AccountName, ldapCreds.AccountPassword);
            entry.AuthenticationType = AuthenticationTypes.Secure;
            DirectorySearcher search = new DirectorySearcher();
            search.SearchRoot = entry;
            search.Filter = "(&(objectClass=user)(SAMAccountName=" + ldapCreds.AccountName + "))";
            //SearchResultCollection result = (SearchResultCollection)this.Cxn.query("(&(objectClass=user)(SAMAccountName=" + credentials.AccountName + "))");

            SearchResultCollection result = search.FindAll();
            if (result.Count != 1)
            {
                throw new MdoException("Invalid credentials");
            }

            return result[0].GetDirectoryEntry().Guid.ToString();
        }

        public override User authorize(AbstractCredentials credentials, AbstractPermission permission)
        {
            if (!(credentials is LdapCredentials))
            {
                throw new MdoException("Invalid credentials - not LDAP credentials");
            }

            // cxn.query only returns a SearchResultCollection so need to manually impersonate here
            LdapCredentials ldapCreds = (LdapCredentials)credentials;

            PrincipalContext principal = new PrincipalContext(ContextType.Domain);
            UserPrincipal user = UserPrincipal.FindByIdentity(principal, ((LdapCredentials)credentials).AccountName);

            if (user != null)
            {
                GroupPrincipal group = GroupPrincipal.FindByIdentity(new PrincipalContext(ContextType.Domain), IdentityType.DistinguishedName, permission.Name);

                if (group == null)
                {
                    throw new MdoException("Unable to locate that group in Active Directory");
                }

                if (user.IsMemberOf(group))
                {
                    User u = new User();
                    u.PrimaryPermission = new LdapGroup() { IsPrimary = true, Name = permission.Name, PermissionId = group.Guid.ToString() };
                    u.Domain = base.Cxn.DataSource.Provider;
                    u.UserName = user.SamAccountName;
                    return u;
                }
            }
            // if we reached this point, authorization was unsuccessful - throw error
            throw new MdoException("User is not a member of that group");
        }

        public override User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            authenticate(credentials);
            return authorize(credentials, permission);
        }
    }
}
