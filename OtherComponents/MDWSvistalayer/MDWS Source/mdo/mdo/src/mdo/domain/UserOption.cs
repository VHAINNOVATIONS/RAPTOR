using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class UserOption
    {
        string number;
        string id;
        string name;
        string displayName;
        string key;
        string reverseKey;
        string type;
        bool primaryOption;

        public UserOption() { }

        public UserOption(string number, string id, string name)
        {
            Number = number;
            Id = id;
            Name = name;
            PrimaryOption = false;
        }

        public UserOption(string number, string id, string name, bool primaryOption)
        {
            Number = number;
            Id = id;
            Name = name;
            PrimaryOption = primaryOption;
        }

        public string Number
        {
            get { return number; }
            set { number = value; }
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

        public string Key
        {
            get { return key; }
            set { key = value; }
        }

        public string ReverseKey
        {
            get { return reverseKey; }
            set { reverseKey = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public bool PrimaryOption
        {
            get { return primaryOption; }
            set { primaryOption = value; }
        }
    }
}
