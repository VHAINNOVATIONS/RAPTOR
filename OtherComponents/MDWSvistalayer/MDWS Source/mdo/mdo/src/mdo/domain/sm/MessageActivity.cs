using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class MessageActivity : BaseModel
    {
        public long UserId { get; set; }

        public sm.enums.ActivityEnum Action { get; set; }

        public sm.enums.UserTypeEnum PerformerType { get; set; }

        public String Detail { get; set; }

        public long MessageId { get; set; }

        public override string ToString()
        {
            StringBuilder sb = new StringBuilder();
            sb.AppendLine();
            sb.AppendLine("New Message Activity (" + DateTime.Now.ToString() + ")");
            sb.AppendLine("======================================================");
            sb.AppendLine("Activity ID: " + this.Id);
            sb.AppendLine("Action Code Name: " + Enum.GetName(typeof(domain.sm.enums.ActivityEnum), this.Action) + ", Action Code Value: " + ((Int32)this.Action).ToString());
            sb.AppendLine("Detail: " + this.Detail);
            sb.AppendLine("Message ID: " + this.MessageId);
            sb.AppendLine("User ID: " + this.UserId);
            sb.AppendLine("======================================================");
            return sb.ToString();
        }
    }
}
