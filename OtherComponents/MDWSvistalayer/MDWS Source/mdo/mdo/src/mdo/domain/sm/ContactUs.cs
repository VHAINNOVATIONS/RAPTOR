using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class ContactUs : BaseModel
    {
        public string Comments { get; set; }
        public string EmailAddress { get; set; }
        public string MiddleName { get; set; }
        public string PhoneNumber { get; set; }
        public string Subject { get; set; }
        public string FirstName { get; set; }
        public string LastName { get; set; }
    }
}
