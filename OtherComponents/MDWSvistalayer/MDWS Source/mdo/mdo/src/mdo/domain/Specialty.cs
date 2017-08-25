using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Specialty
    {
        string id;
        string name;
        string displayName;
        string service;
        string ptfCode;

        public Specialty() { }

        public Specialty(string[] values)
        {
            Id = values[0];
            Name = values[1];
            DisplayName = values[2];
            Service = values[3];
            PtfCode = values[4];
        }

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

        public string DisplayName
        {
            get { return displayName; }
            set { displayName = value; }
        }

        public string Service
        {
            get { return service; }
            set { service = value; }
        }

        public string PtfCode
        {
            get { return ptfCode; }
            set { ptfCode = value; }
        }
    }
}
