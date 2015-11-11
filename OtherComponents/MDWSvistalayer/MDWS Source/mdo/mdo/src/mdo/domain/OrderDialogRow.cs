using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderDialogRow
    {
        string rowNum;
        string type;
        string menuId;
        string formId;
        bool autoAccept;
        string displayText;
        string mnemonic;
        bool displayOnly;

        public OrderDialogRow() { }

        public OrderDialogRow(string rowNum)
        {
            RowNum = rowNum;
        }

        public string RowNum
        {
            get { return rowNum; }
            set { rowNum = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public string MenuId
        {
            get { return menuId; }
            set { menuId = value; }
        }

        public string FormId
        {
            get { return formId; }
            set { formId = value; }
        }

        public bool AutoAccept
        {
            get { return autoAccept; }
            set { autoAccept = value; }
        }

        public string DisplayText
        {
            get { return displayText; }
            set { displayText = value; }
        }

        public string Mnemonic
        {
            get { return mnemonic; }
            set { mnemonic = value; }
        }

        public bool DisplayOnly
        {
            get { return displayOnly; }
            set { displayOnly = value; }
        }
    }
}
