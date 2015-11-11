using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class Credentials : BaseModel
    {
        public string Key { get; set; }
        public DateTime ExpirationDate { get; set; }
        public gov.va.medora.mdo.domain.sm.User User { get; set; }
    }
}
