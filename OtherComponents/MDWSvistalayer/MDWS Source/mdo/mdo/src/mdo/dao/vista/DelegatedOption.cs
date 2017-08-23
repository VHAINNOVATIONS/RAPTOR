using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class DelegatedOption : VistaOption
    {
        public DelegatedOption() : base() { }
        public DelegatedOption(string name) : base(name) { }
        public DelegatedOption(string optionId, string name) : base(optionId, name) { }
        public DelegatedOption(string optionId, string name, string recordNumber) : base(optionId, name, recordNumber) { }

        public override PermissionType Type
        {
            get { return PermissionType.DelegatedOption; }
        }
    }
}
