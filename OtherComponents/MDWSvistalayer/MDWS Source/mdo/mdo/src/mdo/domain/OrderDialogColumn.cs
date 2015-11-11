using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderDialogColumn
    {
        string colNum;
        IndexedHashtable rows;

        public OrderDialogColumn() { }

        public OrderDialogColumn(string colNum)
        {
            ColNum = colNum;
            Rows = new IndexedHashtable();
        }

        public string ColNum
        {
            get { return colNum; }
            set { colNum = value; }
        }

        public IndexedHashtable Rows
        {
            get { return rows; }
            set { rows = value; }
        }

        public bool Exists(string rownum)
        {
            if (Rows == null)
            {
                return false;
            }
            return Rows.ContainsKey(rownum);
        }

        public void AddRow(string rownum)
        {
            Rows.Add(rownum, new OrderDialogRow(rownum));
        }

        public OrderDialogRow GetRow(string rownum)
        {
            return (OrderDialogRow)Rows.GetValue(rownum);
        }
    }
}
