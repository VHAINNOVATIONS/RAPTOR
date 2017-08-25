using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public abstract class VistaOption : AbstractPermission
    {
        string displayName;
        SecurityKey key;
        SecurityKey reverseKey;
        string type;

        public VistaOption() : base() { }
        public VistaOption(string name) : base(name) 
        {
            if (String.IsNullOrEmpty(name))
            {
                name = VistaConstants.CPRS_CONTEXT;
            }
            base.Name = name;
        }
        public VistaOption(string optionId, string name) : base(optionId, name) { }
        public VistaOption(string optionId, string name, string recordNumber) : base(optionId,name,recordNumber) {}

        public string DisplayName
        {
            get { return displayName; }
            set { displayName = value; }
        }

        public SecurityKey Key
        {
            get { return key; }
            set { key = value; }
        }

        public SecurityKey ReverseKey
        {
            get { return reverseKey; }
            set { reverseKey = value; }
        }

        public string OptionType
        {
            get { return type; }
            set { type = value; }
        }
    }
}
