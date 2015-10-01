using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.DirectoryServices.ActiveDirectory;

namespace gov.va.medora.mdws.dto.ldap
{
    [Serializable]
    public class DomainArray : AbstractArrayTO
    {
        public DomainTO[] domains;

        public DomainArray() { }

        public DomainArray(SortedList<string, Domain> sortedDomains)
        {
            if (sortedDomains == null || sortedDomains.Count < 1)
            {
                return;
            }

            this.count = sortedDomains.Count;
            domains = new DomainTO[this.count];

            for (int i = 0; i < sortedDomains.Count; i++)
            {
                domains[i] = new DomainTO(sortedDomains.Values[i]);
                domains[i].simpleName = sortedDomains.Keys[i];
            }
        }
    }
}