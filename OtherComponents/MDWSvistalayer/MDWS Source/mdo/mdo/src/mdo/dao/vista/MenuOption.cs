using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class MenuOption : VistaOption
    {
        public MenuOption() : base() { }
        public MenuOption(string name) : base(name) { }
        public MenuOption(string optionId, string name) : base(optionId, name) { }
        public MenuOption(string optionId, string name, string recordNumber) : base(optionId, name, recordNumber) { }
        public MenuOption(string optionId, string name, string recordNumber, bool isPrimary)
            : base(optionId, name, recordNumber)
        {
            IsPrimary = isPrimary;
        }

        public override PermissionType Type
        {
            get { return PermissionType.MenuOption; }
        }
    }
}
