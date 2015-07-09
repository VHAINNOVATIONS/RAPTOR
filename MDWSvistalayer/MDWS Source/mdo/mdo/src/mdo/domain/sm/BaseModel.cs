using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class BaseModel : PersistentObject
    {
        public bool Active { get; set; }
        private DateTime CreatedDate { get; set; }
        private DateTime ModifiedDate { get; set; }

        public BaseModel()
        {
            Active = true;
            // TBD - this seems wrong... from MHV code... Will new created date get set every time?? 
            CreatedDate = new DateTime();
            ModifiedDate = new DateTime();
        }
    }
}
