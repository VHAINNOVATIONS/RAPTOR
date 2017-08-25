using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class AdminRole : BaseModel
    {
        public gov.va.medora.mdo.domain.sm.User User { get; set; }
        public RoleScopeEnum Scope { get; set; }
        public string ScopeId { get; set; }
    }
}
