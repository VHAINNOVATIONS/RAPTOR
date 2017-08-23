using System;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Symptom : Observation
    {
        public static string SYMPTOM = "Symptom";

        string id;
        string name;
        bool isNational;
        string vuid;

        public Symptom() { }

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

        public bool IsNational
        {
            get { return isNational; }
            set { isNational = value; }
        }

        public string Vuid
        {
            get { return vuid; }
            set { vuid = value; }
        }
    }
}
