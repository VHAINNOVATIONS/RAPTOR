using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class ObservationType
    {
        string id;
        string category;
        string name;
        string shortName;
        string dataId;
        string dataName;
        string dataType;

        public ObservationType(string id, string category, string name)
        {
            Id = id;
            Category = category;
            Name = name;
        }

        public ObservationType() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Category
        {
            get { return category; }
            set { category = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string ShortName
        {
            get { return shortName; }
            set { shortName = value; }
        }

        public string DataId
        {
            get { return dataId; }
            set { dataId = value; }
        }

        public string DataName
        {
            get { return dataName; }
            set { dataName = value; }
        }

        public string DataType
        {
            get { return dataType; }
            set { dataType = value; }
        }
    }
}
