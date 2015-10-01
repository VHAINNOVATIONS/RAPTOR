using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class RatedDisability
    {
        string id;
        string name;
        string percent;
        bool serviceConnected;
        string extremityAffected;
        string originalEffectiveDate;
        string currentEffectiveDate;

        public RatedDisability() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string Percent
        {
            get { return percent; }
            set { percent = value; }
        }

        public bool ServiceConnected
        {
            get { return serviceConnected; }
            set { serviceConnected = value; }
        }

        public string ExtremityAffected
        {
            get { return extremityAffected; }
            set { extremityAffected = value; }
        }

        public string OriginalEffectiveDate
        {
            get { return originalEffectiveDate; }
            set { originalEffectiveDate = value; }
        }

        public string CurrenEffectiveDate
        {
            get { return currentEffectiveDate; }
            set { currentEffectiveDate = value; }
        }
    }
}
