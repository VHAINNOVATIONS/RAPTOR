using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.DirectoryServices;
using System.DirectoryServices.ActiveDirectory;

namespace gov.va.medora.mdo.dao.ldap
{
    public class LdapUtils
    {

        /// <summary>
        /// Translate a friendly domain name (e.g. VHA11) in to a fully qualified name (e.g. V11.MED.VA.GOV)
        /// </summary>
        /// <param name="simpleName">Simple domain name (e.g. VHA11)</param>
        /// <returns>Returns fully qualified name (e.g. V11.MED.VA.GOV)</returns>
        public static String translateSimpleName(String simpleName)
        {
            DirectoryContext context = new DirectoryContext(DirectoryContextType.Domain, simpleName);
            Domain contextDomain = Domain.GetDomain(context);
            return contextDomain.Name;
        }

        /// <summary>
        /// Get a list domains in the current forest. Fetches the simple domain name for each domain
        /// </summary>
        /// <returns>A list of Domains sorted by their simple domain names</returns>
        public static SortedList<string, Domain> getCurrentDomains()
        {
            try
            {
                Forest forest = Domain.GetCurrentDomain().Forest;
                DomainCollection domains = forest.Domains;
                SortedList<string, Domain> sortedList = new SortedList<string, Domain>();

                foreach (Domain d in domains)
                {
                    String distinguishedName = getTranslatableName(d.Name);

                    ActiveDs.NameTranslate tranlator = new ActiveDs.NameTranslate();
                    tranlator.Set((int)ActiveDs.ADS_NAME_TYPE_ENUM.ADS_NAME_TYPE_1779, distinguishedName);
                    String translation = tranlator.Get((int)ActiveDs.ADS_NAME_TYPE_ENUM.ADS_NAME_TYPE_NT4);
                    translation = translation.Replace(@"\", "");
                    sortedList.Add(translation, d);
                }

                return sortedList;
            }
            catch (Exception)
            {
                return null;
            }
        }

        /// <summary>
        /// Return a 'DC=v11,DC=med,DC=va,DC=gov' type String from a 'v11.med.va.gov' formatted string
        /// </summary>
        /// <param name="domain">The domain with period separated DC values</param>
        /// <returns>Distinguished Name string</returns>
        internal static String getTranslatableName(String domain)
        {
            string[] tokens = domain.Split('.');
            StringBuilder sb = new StringBuilder();

            foreach (String s in tokens)
            {
                sb.Append("DC=");
                sb.Append(s);
                sb.Append(",");
            }
            sb.Remove(sb.Length - 1, 1);
            return sb.ToString();
        }

        /// <summary>
        /// Return a 'LDAP://DC=v11,DC=med,DC=va,DC=gov' type String from a 'v11.med.va.gov' formatted string
        /// </summary>
        /// <param name="domain">The domain with period separated DC values</param>
        /// <returns>Distinguished Name string</returns>
        public static String getDistinguishedName(String domain)
        {
            return "LDAP://" + getTranslatableName(domain);
        }

    }
}
